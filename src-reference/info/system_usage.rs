#[cfg(feature = "system-info")]
use sysinfo::System;

/// Get system usage
pub fn get_system_usage() -> (Option<f32>, Option<u64>) {
    #[cfg(feature = "system-info")]
    {
        let mut sys = System::new_all();
        sys.refresh_all();
        let pid = sysinfo::get_current_pid().ok();
        let mut cpu = None;
        let mut mem = None;

        if let Some(pid) = pid {
            if let Some(process) = sys.process(pid) {
                cpu = Some(process.cpu_usage());
                mem = Some(process.memory());
            }
        }

        (cpu, mem)
    }

    #[cfg(not(feature = "system-info"))]
    (None, None)
}

// Tests currently are pretty redundant.
#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_get_system_usage_none_without_feature() {
        #[cfg(not(feature = "system-info"))]
        {
            let (cpu, mem) = get_system_usage();
            assert!(cpu.is_none());
            assert!(mem.is_none());
        }
    }

    #[test]
    #[cfg(feature = "system-info")]
    fn test_get_system_usage_values() {
        let (cpu, mem) = get_system_usage();
        assert!(cpu.is_some());
        assert!(mem.is_some());
    }
}
